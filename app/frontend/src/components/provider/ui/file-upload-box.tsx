import { FileText, Upload, Trash2 } from "lucide-react";
import { Button } from "./button";

interface FileUploadBoxProps {
  label: string;
  fileName: string | null;
  onUpload: () => void;
  onRemove: () => void;
  disabled?: boolean;
}

export function FileUploadBox({
  label,
  fileName,
  onUpload,
  onRemove,
  disabled = false,
}: FileUploadBoxProps) {
  return (
    <div className="flex items-center gap-3 p-4 bg-white rounded-[14px] border-2 border-[#4B236A]/20">
      <FileText className="w-6 h-6 text-[#4B236A] flex-shrink-0" />
      <div className="flex-1 min-w-0">
        <p className="text-sm text-gray-700">{label}</p>
        {fileName && (
          <p className="text-xs text-green-600 mt-1 truncate">
            ✓ {fileName}
          </p>
        )}
      </div>
      {fileName ? (
        <Button
          type="button"
          variant="outline"
          size="sm"
          onClick={onRemove}
          disabled={disabled}
          className="text-red-600 hover:text-red-700 hover:bg-red-50 rounded-[14px] flex-shrink-0"
        >
          <Trash2 className="w-4 h-4" />
        </Button>
      ) : (
        <Button
          type="button"
          size="sm"
          onClick={onUpload}
          disabled={disabled}
          className="bg-[#4B236A] hover:bg-[#4B236A]/90 text-white rounded-[14px] flex-shrink-0"
        >
          <Upload className="w-4 h-4 mr-2" />
          Cargar
        </Button>
      )}
    </div>
  );
}
